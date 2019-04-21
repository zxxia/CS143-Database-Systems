/*
* Copyright (C) 2008 by The Regents of the University of California
* Redistribution of this file is permitted under the terms of the GNU
* Public License (GPL).
*
* @author Junghoo "John" Cho <cho AT cs.ucla.edu>
* @date 3/24/2008
*/

#include "BTreeIndex.h"
#include "BTreeNode.h"

#include <string.h>
#include <stdio.h>



using namespace std;

/*
* BTreeIndex constructor
*/
BTreeIndex::BTreeIndex()
{
	rootPid = -1;
}

/*
* Open the index file in read or write mode.
* Under 'w' mode, the index file should be created if it does not exist.
* @param indexname[IN] the name of the index file
* @param mode[IN] 'r' for read, 'w' for write
* @return error code. 0 if no error
*/
RC BTreeIndex::open(const string& indexname, char mode)
{
	RC rc;
	rc = pf.open(indexname, mode);

	// successfully open/create index file
	if(rc == 0){

		// buffer declareed to store 4-byte rootPid and 4-byte treeheight 
		char buffer[PageFile::PAGE_SIZE];
		if(pf.endPid() == 0){
			// if index file is empty
			// allocate a page to store rootPid and treeHeight
			treeHeight = 0;
			*((int*)buffer) = rootPid;
			*((int*)buffer + 1) = treeHeight;
			rc = pf.write(0, buffer);
		}
		else{
			// index file is not empty
			// read 1st page to buffer
			rc = pf.read(0, buffer);
			rootPid = *((int*)buffer);
			treeHeight = *((int*)buffer + 1);
		}
	}
	return rc;
}

	/*
	* Close the index file.
	* @return error code. 0 if no error
	*/
RC BTreeIndex::close()
{
	RC rc;
	char buffer[PageFile::PAGE_SIZE];
	memcpy(buffer, &rootPid, sizeof(PageId));
	memcpy(buffer + sizeof(PageId), &treeHeight, sizeof(int));
	rc = pf.write(0, buffer);
	return rc;
}

	/*
	* Insert (key, RecordId) pair to the index.
	* @param key[IN] the key for the value inserted into the index
	* @param rid[IN] the RecordId for the record being inserted into the index
	* @return error code. 0 if no error
	*/
RC BTreeIndex::insert(int key, const RecordId& rid)
{
	//if there is no page/tree
	if(rootPid==-1){
		rootPid=pf.endPid();
		BTLeafNode leaf;
		leaf.insert(key,rid);
		treeHeight=1;
		return leaf.write(pf.endPid(),pf);
	}

	//find and insert the key
	PageId leftPid = -1;
	PageId rightPid = -1;
	int key1 = key;
	findandoverflow(treeHeight, key1, rid, rootPid, leftPid, rightPid);

	//new root
	if(leftPid != -1 && rightPid != -1){
		PageId newPid = pf.endPid();
		rootPid = newPid;
		treeHeight++;
		BTNonLeafNode nonleaf;
		nonleaf.initializeRoot(leftPid,key1,rightPid);
		return nonleaf.write(newPid,pf);
	}

	return 0;	
}

	//in the "locate part" key is the key we want to insert, 
	//in the "intsert part", it's the midkey we want to pass on to the upper layer
	//pid is the pageid of the current node
	//the default value of pid1, pid2 is -1
	//if there is overflow during the insertion, we change the value of pid1,pid2 and pass on them to the upper layer    
RC BTreeIndex::findandoverflow(int h, int &key, const RecordId& rid, PageId pid, PageId& leftPid, PageId& rightPid){
	if(h==1){

		//we have reached the leafnode now we should insert the new key
		BTLeafNode leaf;
		leaf.read(pid,pf);

		//no overflow
		if(leaf.getKeyCount()<84) {
			leaf.insert(key,rid);
			return leaf.write(pid,pf);
		}

		//overflow
		PageId newpid = pf.endPid();
		int siblingKey = 0;
		BTLeafNode sibling;
		leaf.insertAndSplit(key, rid, sibling, siblingKey);
		sibling.setNextNodePtr(leaf.getNextNodePtr());
		leaf.setNextNodePtr(newpid);
		leaf.write(pid,pf);
		sibling.write(newpid,pf);
		leftPid = pid;
		rightPid = newpid;
		key = siblingKey;
		return 0;
	}


	//locate and insert the key in the lower layers
	PageId ppid = 0;
	BTNonLeafNode nonleaf;
	nonleaf.read(pid, pf);
	nonleaf.locateChildPtr(key, ppid);


	//the recursion!
	findandoverflow(h-1, key, rid, ppid, leftPid, rightPid);

	if(leftPid == -1 && rightPid == -1) return 0;

	//if pid1 , pid2 ! = -1 it means that there is oeverflow, we have to change the nonleafnode structure  
	// insert, no nonleaf overflow
	if(nonleaf.getKeyCount() < 84){
		nonleaf.insert(key, rightPid);
		leftPid = -1;
		rightPid = -1;
		return nonleaf.write(pid, pf);
	}

	//non-leaf overflow
	BTNonLeafNode sibling;
	int midKey = 0;
	PageId newPid = pf.endPid();
	nonleaf.insertAndSplit(key, rightPid, sibling, midKey);
	sibling.write(newPid, pf);
	nonleaf.write(pid, pf);
	leftPid = pid;
	rightPid = newPid;
	key = midKey;
	return 0;	
}

/**
* Run the standard B+Tree key search algorithm and identify the
* leaf node where searchKey may exist. If an index entry with
* searchKey exists in the leaf node, set IndexCursor to its location
* (i.e., IndexCursor.pid = PageId of the leaf node, and
* IndexCursor.eid = the searchKey index entry number.) and return 0.
* If not, set IndexCursor.pid = PageId of the leaf node and
* IndexCursor.eid = the index entry immediately after the largest
* index key that is smaller than searchKey, and return the error
* code RC_NO_SUCH_RECORD.
* Using the returned "IndexCursor", you will have to call readForward()
* to retrieve the actual (key, rid) pair from the index.
* @param key[IN] the key to find
* @param cursor[OUT] the cursor pointing to the index entry with
*                    searchKey or immediately behind the largest key
*                    smaller than searchKey.
* @return 0 if searchKey is found. Othewise an error code
*/
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
	if(rootPid==-1) return RC_NO_SUCH_RECORD;
	return re_locate(searchKey,rootPid,treeHeight,cursor);	
}


RC BTreeIndex::re_locate(int searchKey, PageId pid, int h, IndexCursor &cursor){
	// has reached the leafnode 
	if(h==1){
		cursor.pid=pid;
		BTLeafNode leaf;
		leaf.read(pid,pf);
		return leaf.locate(searchKey,cursor.eid);
	}

	//traverse the nonleafnode
	BTNonLeafNode nonleaf;
	nonleaf.read(pid,pf);
	PageId newpid=0;
	nonleaf.locateChildPtr(searchKey, newpid);
	return re_locate(searchKey, newpid, h-1, cursor);
}

/*
* Read the (key, rid) pair at the location specified by the index cursor,
* and move foward the cursor to the next entry.
* @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
* @param key[OUT] the key stored at the index cursor location.
* @param rid[OUT] the RecordId stored at the index cursor location.
* @return error code. 0 if no error
*/
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
	if(cursor.pid >= pf.endPid())
		return RC_INVALID_CURSOR;
	RC rc;
	BTLeafNode leaf;
	rc = leaf.read(cursor.pid, pf);
	if(cursor.eid >= leaf.getKeyCount())
		return RC_INVALID_CURSOR;
	if(cursor.eid < leaf.getKeyCount()-1)
		rc = leaf.readEntry(cursor.eid++, key, rid);
	else{
		rc = leaf.readEntry(cursor.eid, key, rid);
		if(leaf.getNextNodePtr() == 0)
			cursor.eid = 84;
		else{
			cursor.pid = leaf.getNextNodePtr();
			cursor.eid = 0;
		}
	}
	return rc;
}








//test functions
/*void BTreeIndex::printTree(PageId root_pid, int height)
{
	if(root_pid == rootPid){
		printf("%s %d\n", "TreeHeight", treeHeight);
		printf("%s %d\n", "RootPid:.", root_pid);
	}
	if(height == 1){
		// print leaf node
		printf("LeafNode PageId: %d\n", root_pid);
		BTLeafNode leaf;
		leaf.read(root_pid, pf);
		leaf.printBuffer();
	}
	else{
		// print non leaf node
		printf("NonLeafNode PageId: %d\n", root_pid);
		BTNonLeafNode nonleaf;
		nonleaf.read(root_pid, pf);
		nonleaf.printBuffer();
		for(int i = 0; i < nonleaf.getKeyCount(); i++){
			
			if(i == 0){
				PageId pid = *((int*)(nonleaf.getBuffer())+1);
				printf("%d", i);
				printTree(pid, height-1);
			}
			printf("%d", i+1);
			PageId pid = *((int*)(nonleaf.getBuffer())+i*2+3);
			printTree(pid, height-1);
		}
	}
}

PageId BTreeIndex::getRootId()
{
	return rootPid;
}
int BTreeIndex::getTreeHeight()
{
	return treeHeight;
}*/